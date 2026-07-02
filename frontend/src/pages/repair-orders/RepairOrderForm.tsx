import { useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { repairOrderService } from '@/services/repair-order.service'
import { NepaliDatePicker } from '@/components/nepali-date/NepaliDatePicker'
import { t } from '@/i18n'
import { useAuth } from '@/stores/auth'
import { Save, Send, Printer, FileDown, ArrowLeft } from 'lucide-react'
import type { Department } from '@/types'
import api from '@/lib/api'
import type { ApiResponse } from '@/types'

const formSchema = z.object({
  date_bs: z.string().min(1, 'Required'),
  department_id: z.number().min(1, 'Required'),
  equipment_name: z.string().min(1, 'Required'),
  problem_description: z.string().min(1, 'Required'),
  estimated_cost: z.number().optional(),
  remarks: z.string().optional(),
})

type FormData = z.infer<typeof formSchema>

export function RepairOrderForm() {
  const { id } = useParams()
  const isEdit = !!id
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { user } = useAuth()

  const { data: departments } = useQuery({
    queryKey: ['departments'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<Department[]>>('/departments')
      return res.data.data
    },
  })

  const { data: formData } = useQuery({
    queryKey: ['repair-order', id],
    queryFn: () => repairOrderService.getById(Number(id)),
    enabled: isEdit,
  })

  const { register, handleSubmit, setValue, watch, formState: { errors }, reset } = useForm<FormData>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      date_bs: '',
      department_id: 0,
      equipment_name: '',
      problem_description: '',
      estimated_cost: undefined,
      remarks: '',
    },
  })

  useEffect(() => {
    if (formData) {
      reset({
        date_bs: formData.date_bs,
        department_id: formData.department_id,
        equipment_name: formData.equipment_name,
        problem_description: formData.problem_description,
        estimated_cost: formData.estimated_cost,
        remarks: formData.remarks || '',
      })
    }
  }, [formData, reset])

  const mutation = useMutation({
    mutationFn: (data: FormData & { status?: string }) =>
      isEdit
        ? repairOrderService.update(Number(id), data)
        : repairOrderService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['repair-orders'] })
      navigate('/repair-orders')
    },
  })

  const submitMutation = useMutation({
    mutationFn: (data: FormData) =>
      repairOrderService.create({ ...data, status: 'submitted' }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['repair-orders'] })
      navigate('/repair-orders')
    },
  })

  const onSubmit = (data: FormData) => mutation.mutate(data)
  const onSubmitSubmit = (data: FormData) => submitMutation.mutate(data)
  const handleDateChange = (v: string) => setValue('date_bs', v)

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <button onClick={() => navigate('/repair-orders')} className="btn-secondary">
          <ArrowLeft className="h-4 w-4" />
        </button>
        <div>
          <h1 className="page-title">{isEdit ? t('repairOrder.edit') : t('repairOrder.create')}</h1>
        </div>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        <div className="card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('repairOrder.repairNumber')}</h2>
          </div>
          <div className="card-body grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label className="form-label">{t('repairOrder.date')}</label>
              <NepaliDatePicker
                value={watch('date_bs')}
                onChange={handleDateChange}
                error={errors.date_bs?.message}
              />
            </div>
            <div>
              <label className="form-label">{t('repairOrder.department')}</label>
              <select {...register('department_id', { valueAsNumber: true })} className="form-input">
                <option value="">Select</option>
                {departments?.map((d: Department) => (
                  <option key={d.id} value={d.id}>{d.name}</option>
                ))}
              </select>
              {errors.department_id && <p className="form-error">{errors.department_id.message}</p>}
            </div>
            <div>
              <label className="form-label">{t('materialRequest.applicant')}</label>
              <input type="text" value={user?.name || ''} className="form-input" disabled />
            </div>
          </div>
        </div>

        <div className="card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('repairOrder.equipmentName')}</h2>
          </div>
          <div className="card-body grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label className="form-label">{t('repairOrder.equipmentName')}</label>
              <input {...register('equipment_name')} className="form-input" placeholder="e.g. Desktop Computer" />
              {errors.equipment_name && <p className="form-error">{errors.equipment_name.message}</p>}
            </div>
            <div>
              <label className="form-label">{t('repairOrder.estimatedCost')}</label>
              <input type="number" {...register('estimated_cost', { valueAsNumber: true })} className="form-input" placeholder="Rs." />
            </div>
          </div>
        </div>

        <div className="card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('repairOrder.problemDescription')}</h2>
          </div>
          <div className="card-body">
            <textarea {...register('problem_description')} rows={4} className="form-input" placeholder="Describe the problem..." />
            {errors.problem_description && <p className="form-error">{errors.problem_description.message}</p>}
          </div>
        </div>

        <div className="card">
          <div className="card-header">
            <h2 className="text-lg font-semibold">{t('repairOrder.remarks')}</h2>
          </div>
          <div className="card-body">
            <textarea {...register('remarks')} rows={3} className="form-input" />
          </div>
        </div>

        <div className="flex items-center justify-between">
          <button type="button" onClick={() => navigate('/repair-orders')} className="btn-secondary">
            {t('common.cancel')}
          </button>
          <div className="flex gap-2">
            <button type="submit" className="btn-secondary gap-2">
              <Save className="h-4 w-4" />
              {t('repairOrder.save')}
            </button>
            <button
              type="button"
              onClick={handleSubmit(onSubmitSubmit)}
              className="btn-primary gap-2"
            >
              <Send className="h-4 w-4" />
              {t('repairOrder.submit')}
            </button>
            {isEdit && (
              <>
                <button type="button" className="btn-secondary gap-2">
                  <Printer className="h-4 w-4" />
                  {t('repairOrder.print')}
                </button>
                <button type="button" onClick={() => repairOrderService.downloadPdf(Number(id))} className="btn-secondary gap-2">
                  <FileDown className="h-4 w-4" />
                  {t('repairOrder.downloadPdf')}
                </button>
              </>
            )}
          </div>
        </div>
      </form>
    </div>
  )
}
